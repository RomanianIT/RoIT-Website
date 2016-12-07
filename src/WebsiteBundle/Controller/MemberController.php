<?php

namespace WebsiteBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use WebsiteBundle\Entity\Member;
use WebsiteBundle\Form\MemberType;


class MemberController extends Controller
{

    /**
     * Admin members - main action
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $members = $em->getRepository('WebsiteBundle:Member')->findAll();

        return $this->render('AdminBundle:Sections/Member:index.html.twig', array(
            'members' => $members,
        ));
    }

    /**
     * Admin members - show member details page
     * @param Member $member
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Member $member)
    {
        $deleteForm = $this->createDeleteForm($member);
        return $this->render('AdminBundle:Sections/Member:show.html.twig', array(
            'member' => $member,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Admin members - edit action
     * @param Request $request
     * @param Member $member
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editAction(Request $request, Member $member)
    {
        $deleteForm = $this->createDeleteForm($member);
        $editForm = $this->createForm('WebsiteBundle\Form\MemberType', $member);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($member);
            $em->flush();

            $this->addFlash(
                'notice',
                'Modificările au fost salvate!'
            );

            return $this->redirectToRoute('website_admin_members_edit', array('id' => $member->getId()));
        }


        return $this->render('AdminBundle:Sections/Member:edit.html.twig', array(
            'member' => $member,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    public function deleteAction(Request $request, Member $member)
    {
        $form = $this->createDeleteForm($member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($member);
            $em->flush();
        }

        return $this->redirectToRoute('website_admin_members_main');
    }

    /**
     * Creates a form to delete a Member entity.
     * @param Member $member The Member entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Member $member)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('website_admin_members_delete', array('id' => $member->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
