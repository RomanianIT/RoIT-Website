<?php

namespace WebsiteBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use WebsiteBundle\Entity\Center;
use WebsiteBundle\Form\CenterType;

class CenterController extends Controller
{
    /**
     * Centers main action
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $centers = $em->getRepository('WebsiteBundle:Center')->findAll();
        return $this->render('AdminBundle:Sections/Center:index.html.twig',[
            'centers' => $centers
        ]);
    }

    /**
     * Create new Center entity
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        $center = new Center();
        $form = $this->createForm('WebsiteBundle\Form\CenterType', $center);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($center);
            $em->flush();

            return $this->redirectToRoute('website_admin_centers_show', array('id' => $center->getId()));
        }

        return $this->render('AdminBundle:Sections/Center:new.html.twig',[
            'center' => $center,
            'form' => $form->createView()
        ]);
    }


    /**
     * @param Center $center
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Center $center)
    {
        $deleteForm = $this->createDeleteForm($center);
        return $this->render('AdminBundle:Sections/Center:show.html.twig',[
            'center' => $center,
            'delete_form' => $deleteForm->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param Center $center
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, Center $center)
    {
        $deleteForm = $this->createDeleteForm($center);
        $editForm = $this->createForm('WebsiteBundle\Form\CenterType', $center);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($center);
            $em->flush();

            return $this->redirectToRoute('website_admin_centers_show', array('id' => $center->getId()));
        }

        return $this->render('AdminBundle:Sections/Center:edit.html.twig',[
           'center' => $center,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param Center $center
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, Center $center)
    {
        $form = $this->createDeleteForm($center);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($center);
            $em->flush();
        }

        return $this->redirectToRoute('website_admin_centers_main');
    }

    /**
     * Creates a form to delete a Center entity.
     * @param Center $center The Center entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Center $center)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('website_admin_centers_delete', array('id' => $center->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
