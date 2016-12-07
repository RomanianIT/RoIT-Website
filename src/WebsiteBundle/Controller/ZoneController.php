<?php

namespace WebsiteBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use WebsiteBundle\Entity\Zone;
use WebsiteBundle\Form\ZoneType;


class ZoneController extends Controller
{
    /**
     * Zones main action
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $zones = $em->getRepository('WebsiteBundle:Zone')->findAll();
        return $this->render('AdminBundle:Sections/Zone:index.html.twig',[
            'zones' => $zones
        ]);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        $zone = new Zone();
        $form = $this->createForm('WebsiteBundle\Form\ZoneType', $zone);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($zone);
            $em->flush();

            return $this->redirectToRoute('website_admin_zones_show', array('id' => $zone->getId()));
        }

        return $this->render('AdminBundle:Sections/Zone:new.html.twig',[
            'zone' => $zone,
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Zone $zone
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Zone $zone)
    {
        $deleteForm = $this->createDeleteForm($zone);

        return $this->render('AdminBundle:Sections/Zone:show.html.twig',[
            'zone' => $zone,
            'delete_form' => $deleteForm->createView(),
        ]);
    }


    /**
     * @param Request $request
     * @param Zone $zone
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, Zone $zone)
    {
        $deleteForm = $this->createDeleteForm($zone);
        $editForm = $this->createForm('WebsiteBundle\Form\ZoneType', $zone);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($zone);
            $em->flush();

            return $this->redirectToRoute('website_admin_zones_edit', array('id' => $zone->getId()));
        }

        return $this->render('AdminBundle:Sections/Zone:edit.html.twig',[
            'zone' => $zone,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Zone $zone
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, Zone $zone)
    {
        $form = $this->createDeleteForm($zone);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($zone);
            $em->flush();
        }

        return $this->redirectToRoute('website_admin_zones_main');
    }

    /**
     * Creates a form to delete a Zone entity.
     * @param Zone $zone The Zone entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Zone $zone)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('website_admin_zones_delete', array('id' => $zone->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
